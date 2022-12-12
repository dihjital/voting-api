//
//  ErrorView.swift
//  Voting
//
//  Created by Szabolcs Tóth on 24.11.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import SwiftUI

struct ErrorView: View {
    // Properties
    let errorMessage: String
    let viewModel: QuestionsViewModel
    
    var body: some View {
        content
    }
    
    @ViewBuilder var content: some View {
        ZStack {
            Color.red.opacity(0.7)
                .ignoresSafeArea()
            
            VStack {
                Text("💣")
                    .font(.largeTitle)
                
                Text("Error happened!")
                    .font(.title)
                    .bold()
                
                Text("\(errorMessage)")
                
                Button {
                    Task {
                        await viewModel.getQuestions()
                    }
                } label: {
                    RetryButtonView()
                }
            }
        }
    }
}

extension ErrorView {
    
}

struct ErrorView_Previews: PreviewProvider {
    static var previews: some View {
        ErrorView(errorMessage: "Cannot connect to the server.", viewModel: QuestionsViewModel(service: QuestionService()))
    }
}
