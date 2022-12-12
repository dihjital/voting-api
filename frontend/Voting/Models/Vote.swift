//
//  Vote.swift
//  Voting
//
//  Created by Szabolcs Tóth on 15.10.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import Foundation

struct Vote: Codable {
    let id: Int
    let voteText, numberOfVotes: String
    let questionID: Int
    
    enum CodingKeys: String, CodingKey {
        case id
        case voteText = "vote_text"
        case numberOfVotes = "number_of_votes"
        case questionID = "question_id"
    }
}

/// Dummy data for building the interface
extension Vote {
    static let dummyVote1 = Vote(id: 1,
                                 voteText: "Alias velit at quibusdam error. Earum qui et libero dolor id. Omnis et non temporibus. Necessitatibus omnis reprehenderit ut perspiciatis vel.",
                                 numberOfVotes: "3",
                                 questionID: 1)
    
    static let dummyVote2 = Vote(id: 2,
                                 voteText: "Impedit qui molestiae eius et quia qui nihil. Rerum repellendus facilis itaque ut. Aut sunt magnam in autem non illum fugiat pariatur",
                                 numberOfVotes: "34",
                                 questionID: 2)
    
    static let dummyVote3 = Vote(id: 3,
                                 voteText: "Et qui quis consequatur eveniet praesentium. Quos ut et possimus et laborum. Aut at amet nostrum dolorem nemo occaecati. Perspiciatis dolor occaecati numquam eos.",
                                 numberOfVotes: "1",
                                 questionID: 3)
    
    static let dummyVotes = [dummyVote1, dummyVote2, dummyVote3]
}
